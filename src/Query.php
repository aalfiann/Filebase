<?php  namespace Filebase;


class Query extends QueryLogic
{

    protected $fields  = [];
    protected $limit   = 0;
    protected $offset  = 0;
    protected $sortBy  = ['ASC'];
    protected $orderBy = [''];


    /**
    * $documents
    *
    */
    protected $documents = [];


    //--------------------------------------------------------------------


    /**
    * ->select()
    *
    * Set the selected fields you wish to return from each document
    *
    */
    public function select($fields)
    {
        if (is_string($fields))
        {
            $fields = explode(',',trim($fields));
        }

        if (is_array($fields))
        {
            $this->fields = $fields;
        }

        return $this;
    }


    /**
    * ->where()
    *
    */
    public function where(...$arg)
    {
        $this->addPredicate('and', $arg);

        return $this;
    }


    //--------------------------------------------------------------------


    /**
    * ->andWhere()
    *
    */
    public function andWhere(...$arg)
    {
        $this->addPredicate('and', $arg);

        return $this;
    }


    //--------------------------------------------------------------------


    /**
    * ->orWhere()
    *
    */
    public function orWhere(...$arg)
    {
        $this->addPredicate('or', $arg);

        return $this;
    }


    //--------------------------------------------------------------------


    /**
    * ->limit()
    *
    */
    public function limit($limit, $offset = 0)
    {
        $this->limit   = (int) $limit;

        if ($this->limit === 0)
        {
            $this->limit = 9999999;
        }

        $this->offset  = (int) $offset;

        return $this;
    }


    //--------------------------------------------------------------------


    /**
    * ->orderBy()
    *
    */
    public function orderBy($field, $sort='ASC')
    {
        if (count($this->orderBy) == 1 && $this->orderBy[0] == '') {
            // Just set the initial index
            $this->orderBy[0] = $field;
            $this->sortBy[0]  = strtoupper($sort);
        } else {
            $this->orderBy[] = $field;
            $this->sortBy[]  = strtoupper($sort);
        }

        return $this;
    }


    //--------------------------------------------------------------------


    /**
    * addPredicate
    *
    */
    protected function addPredicate($logic,$arg)
    {
        $this->predicate->add($logic, $arg);
    }


    //--------------------------------------------------------------------


    /**
    * ->getDocuments()
    *
    *
    */
    public function getDocuments()
    {
        return $this->documents;
    }


    //--------------------------------------------------------------------


    /**
    * ->results()
    *
    * @param bool $data_only - default:true (if true only return the documents data not the full object)
    *
    */
    public function results( $data_only = true )
    {
        if ($data_only === true && empty($this->fields))
        {
            return parent::run()->toArray();
        }

        return $this->resultDocuments();
    }


    //--------------------------------------------------------------------


    /**
    * ->resultDocuments()
    * Note: Sort with query limit and offset will not sort all documents. 
    *
    * @param string $name - Is the default document field name. Ex. __id,__created_at and __updated_at
    * @param string $sort - Force sort the document data field in ascending or descending
    * 
    * @return array
    */
    public function resultDocuments($name='',$sort='ASC')
    {
        $list = parent::run()->getDocuments();
        $sort = (($sort=='DESC')?SORT_DESC:SORT_ASC);
        switch($name){
            case '__id':
                foreach ($list as $key => $item) {
                    $timestamps[$key] = $item->getId();
                }
                if(!empty($timestamps)){
                    array_multisort($timestamps, $sort, $list);
                    return $list;
                }
                return [];
            case '__created_at':
                foreach ($list as $key => $item) {
                    $timestamps[$key] = $item->createdAt();
                }
                if(!empty($timestamps)){
                    array_multisort($timestamps, $sort, $list);
                    return $list;
                }
                return [];
            case '__updated_at':
                foreach ($list as $key => $item) {
                    $timestamps[$key] = $item->createdAt();
                }
                if(!empty($timestamps)){
                    array_multisort($timestamps, $sort, $list);
                    return $list;
                }
                return [];
            default:
                return $list;
        }
    }


    //--------------------------------------------------------------------


    /**
    * ->first()
    *
    * @param bool $data_only - default:true (if true only return the documents data not the full object)
    *
    */
    public function first( $data_only = true )
    {
        if ($data_only === true && empty($this->fields))
        {
            $results = parent::run()->toArray();
            return current($results);
        }

        $results = parent::run()->getDocuments();
        return current($results);
    }


    //--------------------------------------------------------------------


    /**
    * ->last()
    *
    * @param bool $data_only - default:true (if true only return the documents data not the full object)
    *
    */
    public function last( $data_only = true )
    {
        if ($data_only === true && empty($this->fields))
        {
            $results = parent::run()->toArray();
            return end($results);
        }

        $results = parent::run()->getDocuments();
        return end($results);
    }

    //--------------------------------------------------------------------


    /**
    * ->count()
    *
    * Count and return the number of documents in array
    *
    */
    public function count()
    {
        $results = parent::run()->getDocuments();
        return count($results);
    }


    //--------------------------------------------------------------------



    /**
    * toArray
    *
    * @param \Filebase\Document
    * @return array
    */
    public function toArray()
    {
        $docs = [];

        if (!empty($this->documents))
        {
            foreach($this->documents as $document)
            {
                $docs[] = (array) $document->getData();
            }
        }

        return $docs;
    }


    //--------------------------------------------------------------------


    public function delete($input)
    {
        $items=$this->resultDocuments();
        $condition=$input;
        foreach($items as $item)
        {
            if(is_object($input))
            {
                $condition=$input($item);
            }
            if($condition)
            {
                $item->delete();
            }
        }
    }

}
